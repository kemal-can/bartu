/**
 * Bartu CRM - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @version   1.1.7
 *
 * @link      Releases - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @copyright Copyright (c) 2019-2022 mail@kemalcan.net
 */
export default {
  props: {
    resourceName: {
      required: true,
      type: String,
    },
  },
  computed: {
    /**
     * The resource record the mixin is embedded to
     *
     * @return {Object}
     */
    resourceRecord() {
      return this.$store.state[this.resourceName].record
    },
  },
  methods: {
    /**
     * Add resource record media
     *
     * @param  {Object} media
     *
     * @return {Void}
     */
    addResourceRecordMedia(media) {
      this.addResourceRecordHasManyRelationship(media, 'media')
    },

    /**
     * Remove resource record media
     *
     * @param  {Object} media
     *
     * @return {Void}
     */
    removeResourceRecordMedia(media) {
      this.removeResourceRecordHasManyRelationship(media.id, 'media')
    },

    /**
     * Add resource record relationship
     *
     * @param  {Object} record
     * @param  {String} relation
     *
     * @return {Void}
     */
    addResourceRecordHasManyRelationship(record, relation) {
      this.$store.commit(
        this.resourceName + '/ADD_RECORD_HAS_MANY_RELATIONSHIP',
        {
          relation: relation,
          item: record,
        }
      )
    },

    /**
     * Update single resource record relationship
     *
     * @param  {Object} record
     * @param  {String} relation
     *
     * @return {Void}
     */
    updateResourceRecordHasManyRelationship(record, relation) {
      this.$store.commit(
        this.resourceName + '/UPDATE_RECORD_HAS_MANY_RELATIONSHIP',
        {
          relation: relation,
          id: record.id,
          item: record,
        }
      )
    },

    /**
     * Remove single relationship from resource record
     *
     * @param  {Number} id
     * @param  {String} relation
     *
     * @return {Void}
     */
    removeResourceRecordHasManyRelationship(id, relation) {
      this.$store.commit(
        this.resourceName + '/REMOVE_RECORD_HAS_MANY_RELATIONSHIP',
        {
          relation: relation,
          id: id,
        }
      )
    },

    /**
     * Add resource record sub relation
     *
     * @param {string} relation
     * @param {Number} relationId
     * @param {String} subRelation
     * @param {Object} record
     */
    addResourceRecordSubRelation(relation, relationId, subRelation, record) {
      this.$store.commit(
        this.resourceName + '/ADD_RECORD_HAS_MANY_SUB_RELATION',
        {
          relation: relation,
          relation_id: relationId,
          sub_relation: subRelation,
          item: record,
        }
      )
    },

    /**
     * Update resource record sub relation
     *
     * @param {string} relation
     * @param {Number} relationId
     * @param {String} subRelation
     * @param {Object} record
     */
    updateResourceRecordSubRelation(relation, relationId, subRelation, record) {
      this.$store.commit(
        this.resourceName + '/UPDATE_RECORD_HAS_MANY_SUB_RELATION',
        {
          relation: relation,
          relation_id: relationId,
          sub_relation: subRelation,
          sub_relation_id: record.id,
          item: record,
        }
      )
    },

    /**
     * Add resource record sub relation
     *
     * @param {string} relation
     * @param {Number} relationId
     * @param {String} subRelation
     * @param {Number} subRelationId
     */
    removeResourceRecordSubRelation(
      relation,
      relationId,
      subRelation,
      subRelationId
    ) {
      this.$store.commit(
        this.resourceName + '/REMOVE_RECORD_HAS_MANY_SUB_RELATION',
        {
          relation: relation,
          relation_id: relationId,
          sub_relation: subRelation,
          sub_relation_id: subRelationId,
        }
      )
    },

    /**
     * Decrement the resource record count property
     *
     * @param {string} key
     *
     * @return {Void}
     */
    decrementResourceRecordCount(key) {
      if (Number(this.resourceRecord[key]) < 1) {
        return
      }

      this.$store.commit(this.resourceName + '/SET_RECORD', {
        [key]: this.resourceRecord[key] - 1,
      })
    },

    /**
     * Increment the resource record count property
     *
     * @param {string} key
     *
     * @return {Void}
     */
    incrementResourceRecordCount(key) {
      this.$store.commit(this.resourceName + '/SET_RECORD', {
        [key]: this.resourceRecord[key] + 1,
      })
    },
  },
}
